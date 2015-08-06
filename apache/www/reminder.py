#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
#  protobot.py
#  
#  Copyright 2015 Silvano Wegener [Protonet GmbH]

import requests, json, time, thread, urllib2
import sys, MySQLdb, os
#from chatterbot import ChatterBotFactory, ChatterBotType

server = '' #Protonet-Server
email = 'foobar@protonet.info' #Protonet-Server-User-Email
password = 'bbbbbbbb' #Protonet-Server-User-Password

class DataBase(object):
	def __init__(self, server, user, password, database):
		self.server = server
		self.user = user
		self.password = password
		self.database = database
		self.connection = MySQLdb.connect(server, user, password, database)
		self.cursor = self.connection.cursor()
		self.thread = thread.start_new_thread(self.reconnect_thread, (None,))
		
	def reconnect_thread(self, value):
		while True:
			time.sleep(3600)
			self.connection.close()
			self.connection = MySQLdb.connect(self.server, self.user, self.password , self.database)
			self.cursor = self.connection.cursor()
			print "mysql reconnect"			

	def get_office_work(self, username):
		office_id = self.get_office_id(username)
		sql = 'select description, user_id from office_work where office_id = ' + str(office_id) + ';'
		self.cursor.execute(sql)
		data = self.cursor.fetchall()
		new_data = []
		for entry in data:
			new_data.append((entry[0],int(entry[1])))
		return new_data
		
	def get_current_worker_id(self, username, key):
		office_id = self.get_office_id(username)
		sql = 'select user_id from office_work where office_id = ' + str(office_id) + ' and description = "'+key+'";'
		self.cursor.execute(sql)
		data = self.cursor.fetchall()
		return int(data[0][0])
	
	def last_user_id(self):
		sql = "SELECT count(id) FROM user";
		self.cursor.execute(sql)
		data = self.cursor.fetchall()
		return int(data[0][0])
		
	def next(self, username, key):
		office_id = self.get_office_id(username)
		out_of_office_list = self.get_out_of_office_user_ids(username)
		sql = 'select description, user_id from office_work where office_id = ' + str(office_id) + ' and description = "'+key+'";'
		self.cursor.execute(sql)
		current_muell_id = int(self.cursor.fetchall()[0][1])
		print current_muell_id
		current_muell_id += 1
		while current_muell_id in out_of_office_list:
			current_muell_id += 1
		sql = "UPDATE office_work SET user_id = "+ str(current_muell_id)+" WHERE office_id = " + str(office_id) + " AND description = '"+key+"';";
		self.cursor.execute(sql)
		self.connection.commit()
		
	def get_out_of_office_user_ids(self, username):
		office_id = self.get_office_id(username)
		sql = 'select * from user where out_of_office = 1;'
		self.cursor.execute(sql)
		data = self.cursor.fetchall()
		new_data = []
		for entry in data:
			new_data.append(int(entry[0]))
		return new_data

	def user_id_from_name(self, username):
		sql = 'select id from user where username = "' + username + '";'
		self.cursor.execute(sql)
		data = self.cursor.fetchone()
		if data == None:
			return 0
		return int(data[0])		
		
	def get_out_of_office_user(self, username):
		office_id = self.get_office_id(username)
		sql = 'select * from user where out_of_office = 1;'
		self.cursor.execute(sql)
		data = self.cursor.fetchall()
		new_data = []
		for entry in data:
			new_data.append(str(entry[1]+' '+entry[2]).replace(os.linesep,''))
		return new_data
		
	def get_in_office_user(self, username):
		office_id = self.get_office_id(username)
		sql = 'select * from user where out_of_office = 0;'
		self.cursor.execute(sql)
		data = self.cursor.fetchall()
		new_data = []
		for entry in data:
			new_data.append(str(entry[1]+' '+entry[2]).replace(os.linesep,''))
		return new_data
		
	def get_office_id(self, username):
		sql = 'select id from office where office = "' + username + '";'
		self.cursor.execute(sql)
		data = self.cursor.fetchone()
		return int(data[0])

	def get_username_by_db_id(self, id, more_info=False):
		if more_info:
			sql = 'select concat(first_name, last_name) from user where id = ' + str(id) + ';'
		else:
			sql = 'select username from user where id = ' + str(id) + ';'
		self.cursor.execute(sql)
		data = self.cursor.fetchone()
		return data[0]

	def is_user_out_of_office(self, username):
		sql = 'select out_of_office from user where username = "' + username + '";'
		self.cursor.execute(sql)
		data = self.cursor.fetchone()
		if data == None:
			return None
		if data[0] == 1:
			return True
		return False		

	def set_user_out_of_office(self, username, value=False):
		sql = 'update user set out_of_office = 0 where username = "' + username + '";'
		if value:
			sql = 'update user set out_of_office = 1 where username = "' + username + '";'
		try:
			self.cursor.execute(sql)
			self.connection.commit()
			return True	   
		except:
			self.connection.rollback()
			return False	

	def get_private_chat_ids(self):
		sql = 'select id from private_chat'
		self.cursor.execute(sql)
		data = self.cursor.fetchall()
		chat_ids = []
		for row in data:
			chat_ids.append(int(row[0]))
		return sorted(chat_ids)

	def add_private_chat(self, id):
		sql = 'insert into private_chat (id, last_seen_meep) values ('+str(id)+', 0);'
		try:
			self.cursor.execute(sql)
			self.connection.commit()
			return True	   
		except:
			self.connection.rollback()
			return False		

	def create_private_chat(self, chat_id):
		sql = 'insert into office_clean.private_chat (id, last_seen_meep) values ('+str(chat_id)+', 0);'
		try:
			self.cursor.execute(sql)
			self.connection.commit()
			return True	   
		except:
			self.connection.rollback()
			return False
	
	    		
		
		
		self.cursor.execute(sql)
		data = self.cursor.fetchall()
		chat_ids = []
		for row in data:
			chat_ids.append(int(row[0]))
		return sorted(chat_ids)

	def __del__(self):
		self.connection.close()

class ProtonetServerConnection(object):
	def __init__(self, server, user, password):
		self.server = server
		self.url = "https://"+self.server+'/api/v1/'
		self.user = user
		self.password = password
		self.auth = (self.user, self.password)
		self.users = self.get_users()
		self.me = self.__get_own_data()
		self.username = self.me['username']
		self.first_name = self.me['first_name']
		self.last_name = self.me['last_name']
		self.email = self.me['email']
		self.id = self.me['id']

	def __get_own_data(self):
		response = requests.get(self.url + 'me', auth=self.auth, verify=False)
		data = response.json()['me']	
		return data

	def get_users(self):
		response = requests.get(self.url + 'users', auth=self.auth, verify=False)
		users = response.json()['users']
		data = {}
		for user in users:
			entry = {}
			entry['first_name'] = user['first_name']
			entry['last_name'] = user['last_name']
			entry['url'] = user['url']
			entry['deactivated'] = user['deactivated']
			entry['email'] = user['email']
			entry['role'] = user['role']
			entry['avatar']	= user['avatar']
			entry['online'] = user['online']
			entry['id'] = user['id']
			entry['last_active_at'] = user['last_active_at']
			data[user['username']] = entry
		return data

	def get_private_chats(self):
		data = {}
		response = requests.get(self.url + 'private_chats', auth=self.auth)
		chats = response.json()['private_chats']
		for chat in chats:
			entry = {}
			entry['username'] =  chat['other_user']['username']
			entry['user_id'] = chat['other_user']['id']
			entry['last_seen_meep_no'] = chat['subscription']['last_seen_meep_no']
			entry['subscription_id'] = os.path.split(chat['subscription']['url'])[1]
			data[chat['id']] = entry
		return data

	def get_private_chat_ids(self):
		data = self.get_private_chats().keys()
		return sorted(data)

	def get_private_chats_content(self, chat_id=False):
		chats = self.get_private_chats()
		data = {}
		if chat_id == False:
			for key in chats.keys():
				data[key] = []
				response = requests.get(self.url + 'private_chats/' + str(key) + '/meeps?limit=5' , auth=self.auth)
				meeps = response.json()['meeps']
				for id, meep in enumerate(meeps[::-1]):
					entry = self.__create_entry(meep, id, key)
					data[key].append(entry)
		else:
			data = []
			response = requests.get(self.url + 'private_chats/' + str(chat_id) + '/meeps?limit=5' , auth=self.auth)
			meeps = response.json()['meeps']
			for id, meep in enumerate(meeps[::-1]):
				entry = self.__create_entry(meep, id, chat_id)
				data.append(entry)
		return data
		
	def __create_entry(self, meep, id, chat_id):
		entry = {}
		entry['id'] = id
		entry['message'] = meep['message']
		entry['type'] = meep['type']
		entry['files'] = meep['files']
		entry['sender'] = meep['user']['username']
		entry['no'] = meep['no']
		entry['meep_id'] = meep['id']
		entry['private_chat_id'] = chat_id
		return entry	

	def crate_private_chat(self, username):
		headers = {'content-type': 'application/json;charset=UTF-8'}
		user_id = self.users[username]['id']
		data = {'other_user_id':  user_id}
		data_json = json.dumps(data)
		response = requests.post(self.url + 'private_chats', auth=self.auth, data=data_json, headers=headers, verify=False)
		chat = response.json()
		return chat['private_chat']['id']

	def send_private_chat_meep(self, receiver, message, files=[]):
		chat_id = self.crate_private_chat(receiver)
		if chat_id == False:
			return False
		headers = {'content-type': 'application/json;charset=UTF-8'}
		data = {'message':  message}
		data_json = json.dumps(data)
		lurl = self.url + 'private_chats/' + str(chat_id) + '/meeps'
		response = requests.post(lurl , auth=self.auth, data=data_json, headers=headers, verify=False)
		return response

	def set_last_seen_meep(self, chat_id, subscription_id, meep_no):
		headers = {'content-type': 'application/json;charset=UTF-8'}
		data = {'last_seen_meep_no':  meep_no}
		data_json = json.dumps(data)
		response = requests.put(self.url + 'private_chats/' + str(chat_id) + '/subscriptions/'+subscription_id , auth=self.auth, data=data_json, headers=headers)

class ProtoBot(object):
	def __init__(self, database, protonet_server, protonet_username, answers, default_msg='Das habe ich leider nicht verstanden.'):
		#self.database = database
		self.answers = answers
		self.protonet_server = protonet_server
		self.username = protonet_username
		self.notify_days = ['Mon','Tue','Wed','Thu','Fri']
		self.notify_hours = ['09','14','17']
		self.terminate = False
		self.default_msg = default_msg
		#self.robot_thread_run = thread.start_new_thread(self.robot_thread, (None,))
		#self.notify_thread_run = thread.start_new_thread(self.notify_thread, (None,))
		#self.avatar_thread_run = thread.start_new_thread(self.avatar_thread, (None,))
		
		#self.factory = ChatterBotFactory()
		#self.chatterbot = self.factory.create(ChatterBotType.CLEVERBOT)
		#self.chatterbot_session = self.chatterbot.create_session()

	def avatar_thread(self, value):
		while True:
			try:
				self.update_avatars()
			except:
				pass	
			time.sleep(300)
		
	def robot_thread(self, value):
		self.help_message = [
			'Alles was ich kann:',
			'',
			'"hilfe": Diese Übersicht.',
			'"wer ist dran?": Zeigt an wer welchen Dienst hat.',
			'"bin ich da?": Sagt Dir, ob du im Büro bist. (laut Dienstplan)',
			'"ich bin nicht da!": Setzt dich "Out of Office".',
			'"ich bin wieder da!": Setzt dich auf anwesend.',
			'"erledigt" oder "done": Drückt für Dich auf weiter.',
			'',
			'"wer ist nicht da?": Sagt Dir, wer in Deinem Büro nicht da ist.',
			'"wer ist da?": Das Gegenteil',
			'',
			'Du kannst mich auch nach einem Zitat, einem Witz oder dem Wetter fragen :)'
		]
		
		while not self.terminate:
			try:
				for meep in self.get_new_meeps():
					chat_id = meep['private_chat_id']
					subscription_id = meep['subscription_id']
					meep_no = meep['no']
					sender = meep['sender']
					message = meep['message'].lower()
					
					if sender == self.username or 'office' in sender:
						self.set_last_seen_meep(chat_id, subscription_id, meep_no)
						continue
	
					print sender + ": " + message
					
					try:
						if message == 'help' or message == 'hilfe':
							message = '\n'.join(self.help_message)
						elif '!!!' in message:
							message = 'Schrei mich nicht an! :rage:'
							
						elif 'wer ist dran' in message:
							message = self.current_worker_message()
							
						elif 'bin ich da' in message:
							dat = self.database.is_user_out_of_office(sender)
							if dat:
								message = 'Nein :cry:'
							elif dat == None:
								message = 'Keine Ahnung :( Du arbeitest offensichtlich nicht im Office 152.'
							else:
								message = 'Ja :)'
								
						elif 'ich bin nicht da' in message:
							self.database.set_user_out_of_office(sender, True)
							message = 'Schade... :worried:'
							
						elif 'ich bin wieder da' in message or 'ich bin da' in message:
							self.database.set_user_out_of_office(sender, False)	
							message = 'Juhuuu! :D'
						
						elif 'wer ist nicht da' in message:
							ul = self.database.get_out_of_office_user(self.username)
							message = '\n'.join(ul)
							
						elif 'wer ist da' in message:
							ul =  self.database.get_in_office_user(self.username)
							message = '\n'.join(ul)
							
						elif 'erledigt' in message or 'done' in message:
							data = self.database.get_office_work(self.username)
							dict_data = {}
							for key, uid in data:
								dict_data[uid] = key
							user_id = self.database.user_id_from_name(sender)
							try:
								work_key = dict_data[user_id]
								self.database.next(self.username, work_key)
								message = 'Alles klar! ;) Gut gemacht!'
							except:
								message = 'Du bist nicht dran! :P'
							
						
						
						elif 'kaffee' in message or 'coffee' in message:
							message = 'Kaffee? Den musst Du Dir leider selbst holen :P :D'
						elif 'wetter' in message or 'weather' in message:
							try:
								response = urllib2.urlopen('http://api.openweathermap.org/data/2.5/weather?q=hamburg,de')
								response = json.loads(response.read())
								message = 'Diese Wetterinfos konnte ich finden:'
								message += '\n\nWetter: ' + response['weather'][0]['main']
								message += '\nTemperatur: ' + str(response['main']['temp_min']-273.15) + ' - ' +str(response['main']['temp_max']-273.15)+' Grad'
								message += '\nLuftfeuchtigkeit: ' + str(response['main']['humidity'])+'%'						
								message += '\nWindgeschwindigkeit: ' + str(response['wind']['speed']/0.62137)+' km/h'
							except:
								message = 'Oh da konnte ich gerade nichts finden :(\nIch schlage vor, Du schaust aus dem Fenster! ;)'						
						elif 'witz' in message or 'joke' in message:
							message = self.get_fortune()	
						elif 'zitat' in message or 'quote' in message:
							message = self.get_fortune('zitate')					
						else:
							try:
								message = self.chatterbot_session.think(message)
							except:
								message = 'Das habe ich nicht verstanden.'
							
					except UnicodeWarning:
							message = 'Manche Sonderzeichen kann ich noch nicht lesen :( bitte neu formulieren!'
							
							
					self.set_last_seen_meep(chat_id, subscription_id, meep_no)
					self.protonet_server.send_private_chat_meep(sender, message)
			except:
				pass
			time.sleep(3)
	
	def notify_thread(self, value):
		while not self.terminate:
			current_stamp = self.get_timestamp()
			if current_stamp[0] in self.notify_days:
				if current_stamp[1] in self.notify_hours:
					if current_stamp[2] == '00':
						self.send_notifications()
			time.sleep(60)

	def set_all_meeps_as_seen(self):
		private_chats = self.protonet_server.get_private_chats()
		private_chats_content = self.protonet_server.get_private_chats_content()

		for key in private_chats.keys():
			subscription_id = private_chats[key]['subscription_id']
			privat_chat_id = key
			last_meep_id = private_chats_content[key][-1]['no']
			self.protonet_server.set_last_seen_meep(privat_chat_id, subscription_id, last_meep_id)

	def get_new_meeps(self):
		private_chats = self.protonet_server.get_private_chats()
		private_chats_content = self.protonet_server.get_private_chats_content()
		meeps = []
		for key in private_chats.keys():
			last_seen_meep = private_chats[key]['last_seen_meep_no']
			subscription_id = private_chats[key]['subscription_id']
			for meep in private_chats_content[key]:
				if meep['no'] > last_seen_meep:
					meep['subscription_id'] = subscription_id
					meeps.append(meep)
		return meeps
		
	def send_notifications(self):
		messages = {
		'muell': 'Bitte denk dran, dass Du heute noch den Müll runterbringst. Du bist dran! ;) Du brauchst nur den Müll in der Küche runterbringen. Deine netten Kollegen sind dafür verantwortlich, den Müll aus Ihren Büros in die Küche zu bringen.',
		'pfand': 'Bitte denk dran, dass Du heute noch die Pfandflaschen zu Edeka bringst. Du bist dran! ;) Geld danach bitte zu Ina!',
		'aus': 'Bitte denk dran, den Geschirrspüler auszuräumen. Du bist dran! ;)',
		'ein': 'Bitte denk dran, den Geschirrspüler einzuräumen. Du bist dran! ;) und das Abwischen der Flächen in der Küche nicht vergessen :)',
		'kaffee': 'Bitte denk dran, dass Du heute noch den Pappmüll runterbringst. Du bist dran! ;)'
		}							
		office_work = self.database.get_office_work(self.username)
		for work in office_work:
			if work[0] == 'wasser':
				continue
			receiver = self.database.get_username_by_db_id(work[1])
			print self.protonet_server.send_private_chat_meep(receiver, messages[work[0]])
		
	def send_notification(self, work_kind, username):
		messages = {
	'muell': 'Bitte denk dran, dass Du heute noch den Müll runterbringst. Du bist dran! ;) Du brauchst nur den Müll in der Küche runterbringen. Deine netten Kollegen sind dafür verantwortlich, den Müll aus Ihren Büros in die Küche zu bringen.',
	'pfand': 'Bitte denk dran, dass Du heute noch die Pfandflaschen zu Edeka bringst. Du bist dran! ;) Geld danach bitte zu Ina!',
	'aus': 'Bitte denk dran, den Geschirrspüler auszuräumen. Du bist dran! ;)',
	'ein': 'Bitte denk dran, den Geschirrspüler einzuräumen. Du bist dran! ;) und das Abwischen der Flächen in der Küche nicht vergessen :)',
	'kaffee': 'Bitte denk dran, dass Du heute noch den Pappmüll runterbringst. Du bist dran! ;)'
}						
		print self.protonet_server.send_private_chat_meep(username, messages[work_kind])
		
	def set_last_seen_meep(self, chat_id, subscription_id, meep_no):
		self.protonet_server.set_last_seen_meep(chat_id, subscription_id, meep_no)

	def get_timestamp(self):
		stamp = time.strftime("%a_%H_%M").split('_')
		return stamp

	def get_current_worker(self):
		office_work = self.database.get_office_work(self.username)
		data = {}
		for work in office_work:
			info = self.database.get_username_by_db_id(work[1], True)
			data[work[0]] = info
		return data
		
	def current_worker_message(self):
		info = self.get_current_worker()
		message = 'Müll: ' + info['muell'].replace(os.linesep,' ') + '\n'
		message += 'Pfand: ' + info['pfand'].replace(os.linesep,' ') + '\n'
		message += 'Geschirr einräumen: ' + info['geschirr_ein'].replace(os.linesep,' ') + '\n'
		message += 'Geschirr ausräumen: ' + info['geschirr_aus'].replace(os.linesep,' ') + '\n'
		message += 'Wasser nachfüllen: ' + info['wasser'].replace(os.linesep,' ')
		return message

	def update_avatars(self):
		users = self.protonet_server.get_users()
		for user in users.keys():
			response = urllib2.urlopen(users[user]['avatar'])
			print response
			img = response.read()
			with open('avatar/'+users[user]['email']+'.jpg','w') as target:
				target.write(img)

	def get_fortune(self, typ='witze'):
		witz = os.popen('fortune '+ typ).read()
		return witz

	def __del__(self):
		self.terminate = True
		time.sleep(3)



notification_messages = {
	'muell': 'Bitte denk dran, dass Du heute noch den Müll runterbringst. Du bist dran! ;) Du brauchst nur den Müll in der Küche runterbringen. Deine netten Kollegen sind dafür verantwortlich, den Müll aus Ihren Büros in die Küche zu bringen.',
	'pfand': 'Bitte denk dran, dass Du heute noch die Pfandflaschen zu Edeka bringst. Du bist dran! ;) Geld danach bitte zu Ina!',
	'aus': 'Bitte denk dran, den Geschirrspüler auszuräumen. Du bist dran! ;)',
	'ein': 'Bitte denk dran, den Geschirrspüler einzuräumen. Du bist dran! ;) und das Abwischen der Flächen in der Küche nicht vergessen :)',
	'kaffee': 'Bitte denk dran, dass Du heute noch den Pappmüll runterbringst. Du bist dran! ;)'
}	



server = '' #Protonet-Server
email = 'foobar@protonet.info' #Protonet-Server-User-Email
password = 'bbbbbbbb' #Protonet-Server-User-Password

#database = DataBase('localhost','root', 'bbbbbb', 'Office')
database = False


carla = ProtonetServerConnection(server, email, password)
bot = ProtoBot(database, carla, carla.username, False, False)
bot.send_notification(sys.argv[1], sys.argv[2])

